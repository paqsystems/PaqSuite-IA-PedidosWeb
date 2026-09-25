<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

function section(string $title, string $sql): void
{
    echo "\n=== {$title} ===\n";
    try {
        $rows = DB::select($sql);
        echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
    } catch (Throwable $e) {
        echo 'ERROR: '.$e->getMessage()."\n";
    }
}

section('Sesiones ANKA-SRV', <<<'SQL'
SELECT
    s.session_id,
    s.login_name,
    s.host_name,
    s.program_name,
    s.client_interface_name,
    DB_NAME(s.database_id) AS current_db,
    s.status,
    s.open_transaction_count,
    s.cpu_time,
    s.memory_usage,
    s.total_scheduled_time,
    s.total_elapsed_time,
    s.last_request_start_time,
    s.last_request_end_time,
    c.connect_time,
    c.client_net_address,
    c.client_tcp_port,
    c.auth_scheme,
    c.net_transport
FROM sys.dm_exec_sessions s
LEFT JOIN sys.dm_exec_connections c ON c.session_id = s.session_id
WHERE s.is_user_process = 1
  AND s.host_name = 'ANKA-SRV'
ORDER BY s.open_transaction_count DESC, s.last_request_start_time DESC
SQL);

section('Transacciones abiertas ANKA-SRV', <<<'SQL'
SELECT
    s.session_id,
    s.program_name,
    DB_NAME(s.database_id) AS current_db,
    s.status,
    dt.transaction_id,
    dt.transaction_begin_time,
    DATEDIFF(SECOND, dt.transaction_begin_time, SYSDATETIME()) AS open_seconds,
    dt.transaction_type,
    dt.transaction_state
FROM sys.dm_tran_session_transactions st
JOIN sys.dm_tran_active_transactions dt ON st.transaction_id = dt.transaction_id
JOIN sys.dm_exec_sessions s ON s.session_id = st.session_id
WHERE s.host_name = 'ANKA-SRV'
ORDER BY dt.transaction_begin_time
SQL);

section('Ultimo batch / input buffer ANKA-SRV', <<<'SQL'
SELECT
    s.session_id,
    s.program_name,
    s.status,
    s.open_transaction_count,
    ib.event_type,
    ib.parameters,
    ib.event_info AS last_sql_batch
FROM sys.dm_exec_sessions s
OUTER APPLY sys.dm_exec_input_buffer(s.session_id, 0) ib
WHERE s.is_user_process = 1
  AND s.host_name = 'ANKA-SRV'
ORDER BY s.session_id
SQL);

section('Request activo ANKA-SRV (si hay)', <<<'SQL'
SELECT
    r.session_id,
    s.program_name,
    r.status,
    r.command,
    r.blocking_session_id,
    r.wait_type,
    r.wait_time,
    r.cpu_time,
    r.total_elapsed_time,
    DB_NAME(r.database_id) AS db_name,
    SUBSTRING(t.text, (r.statement_start_offset / 2) + 1,
        ((CASE r.statement_end_offset WHEN -1 THEN DATALENGTH(t.text) ELSE r.statement_end_offset END
          - r.statement_start_offset) / 2) + 1) AS running_sql,
    t.text AS full_batch
FROM sys.dm_exec_requests r
JOIN sys.dm_exec_sessions s ON s.session_id = r.session_id
OUTER APPLY sys.dm_exec_sql_text(r.sql_handle) t
WHERE s.host_name = 'ANKA-SRV'
SQL);

section('Locks exclusivos ANKA-SRV (resumen)', <<<'SQL'
SELECT
    l.request_session_id AS session_id,
    s.program_name,
    l.resource_type,
    l.request_mode,
    l.request_status,
    COUNT(*) AS lock_count
FROM sys.dm_tran_locks l
JOIN sys.dm_exec_sessions s ON s.session_id = l.request_session_id
WHERE s.host_name = 'ANKA-SRV'
GROUP BY l.request_session_id, s.program_name, l.resource_type, l.request_mode, l.request_status
ORDER BY lock_count DESC
SQL);

section('Tablas bloqueadas por ANKA-SRV (TOP locks KEY/OBJECT)', <<<'SQL'
SELECT TOP 30
    l.request_session_id AS session_id,
    s.program_name,
    l.resource_type,
    l.request_mode,
    OBJECT_NAME(p.object_id, l.resource_database_id) AS object_name,
    COUNT(*) AS lock_count
FROM sys.dm_tran_locks l
JOIN sys.dm_exec_sessions s ON s.session_id = l.request_session_id
LEFT JOIN sys.partitions p ON p.hobt_id = l.resource_associated_entity_id
WHERE s.host_name = 'ANKA-SRV'
  AND l.resource_database_id = DB_ID('paqsystems_pedidosweb_ankasdelsur')
  AND l.resource_type IN ('KEY', 'OBJECT', 'PAGE')
GROUP BY l.request_session_id, s.program_name, l.resource_type, l.request_mode, p.object_id, l.resource_database_id
ORDER BY lock_count DESC
SQL);

section('Cadena de bloqueo global', <<<'SQL'
SELECT
    r.session_id AS blocked_spid,
    s.host_name AS blocked_host,
    s.program_name AS blocked_program,
    r.blocking_session_id AS blocker_spid,
    bs.host_name AS blocker_host,
    bs.program_name AS blocker_program,
    r.wait_type,
    r.wait_time,
    r.wait_resource
FROM sys.dm_exec_requests r
JOIN sys.dm_exec_sessions s ON s.session_id = r.session_id
LEFT JOIN sys.dm_exec_sessions bs ON bs.session_id = r.blocking_session_id
WHERE r.blocking_session_id <> 0
ORDER BY r.wait_time DESC
SQL);

section('Procesos cliente unicos en la instancia', <<<'SQL'
SELECT
    host_name,
    program_name,
    client_interface_name,
    COUNT(*) AS session_count,
    SUM(open_transaction_count) AS open_transactions
FROM sys.dm_exec_sessions
WHERE is_user_process = 1
GROUP BY host_name, program_name, client_interface_name
ORDER BY open_transactions DESC, session_count DESC
SQL);
