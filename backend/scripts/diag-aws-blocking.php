<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

function section(string $title, string $sql): void
{
    echo "\n=== {$title} ===\n";
    $start = microtime(true);
    try {
        $rows = DB::select($sql);
        echo 'rows='.count($rows).' elapsed_s='.round(microtime(true) - $start, 2)."\n";
        echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
    } catch (Throwable $e) {
        echo 'ERROR: '.$e->getMessage()."\n";
    }
}

section('Sesiones usuario (como SSMS)', <<<'SQL'
SELECT
    s.session_id,
    s.login_name,
    s.host_name,
    s.program_name,
    DB_NAME(s.database_id) AS current_db,
    s.status,
    s.open_transaction_count,
    s.last_request_start_time,
    s.last_request_end_time,
    r.blocking_session_id,
    r.wait_type,
    r.wait_time,
    r.command,
    SUBSTRING(t.text, (r.statement_start_offset / 2) + 1,
        ((CASE r.statement_end_offset WHEN -1 THEN DATALENGTH(t.text) ELSE r.statement_end_offset END
          - r.statement_start_offset) / 2) + 1) AS running_sql
FROM sys.dm_exec_sessions s
LEFT JOIN sys.dm_exec_requests r ON r.session_id = s.session_id
OUTER APPLY sys.dm_exec_sql_text(r.sql_handle) t
WHERE s.is_user_process = 1
ORDER BY s.open_transaction_count DESC, s.last_request_start_time DESC
SQL);

section('Transacciones abiertas', <<<'SQL'
SELECT
    st.session_id,
    s.login_name,
    s.host_name,
    s.program_name,
    DB_NAME(s.database_id) AS current_db,
    s.status,
    st.transaction_id,
    dt.name AS transaction_name,
    dt.transaction_begin_time,
    DATEDIFF(SECOND, dt.transaction_begin_time, SYSDATETIME()) AS open_seconds,
    CASE dt.transaction_type
        WHEN 1 THEN 'read/write'
        WHEN 2 THEN 'read-only'
        WHEN 3 THEN 'system'
        WHEN 4 THEN 'distributed'
        ELSE CAST(dt.transaction_type AS VARCHAR(10))
    END AS transaction_type,
    CASE dt.transaction_state
        WHEN 0 THEN 'not initialized'
        WHEN 1 THEN 'initialized not started'
        WHEN 2 THEN 'active'
        WHEN 3 THEN 'ended'
        WHEN 4 THEN 'commit started'
        WHEN 5 THEN 'prepared'
        WHEN 6 THEN 'committed'
        WHEN 7 THEN 'rolling back'
        WHEN 8 THEN 'rolled back'
        ELSE CAST(dt.transaction_state AS VARCHAR(10))
    END AS transaction_state
FROM sys.dm_tran_session_transactions st
INNER JOIN sys.dm_tran_active_transactions dt ON st.transaction_id = dt.transaction_id
INNER JOIN sys.dm_exec_sessions s ON s.session_id = st.session_id
WHERE s.is_user_process = 1
ORDER BY dt.transaction_begin_time ASC
SQL);

section('Cadena de bloqueo (blocked -> blocker)', <<<'SQL'
SELECT
    r.session_id AS blocked_session_id,
    s.login_name AS blocked_login,
    s.host_name AS blocked_host,
    s.program_name AS blocked_program,
    DB_NAME(r.database_id) AS blocked_db,
    r.blocking_session_id,
    bs.login_name AS blocker_login,
    bs.host_name AS blocker_host,
    bs.program_name AS blocker_program,
    DB_NAME(bs.database_id) AS blocker_db,
    r.wait_type,
    r.wait_time,
    r.wait_resource,
    SUBSTRING(t.text, (r.statement_start_offset / 2) + 1,
        ((CASE r.statement_end_offset WHEN -1 THEN DATALENGTH(t.text) ELSE r.statement_end_offset END
          - r.statement_start_offset) / 2) + 1) AS blocked_sql
FROM sys.dm_exec_requests r
INNER JOIN sys.dm_exec_sessions s ON s.session_id = r.session_id
LEFT JOIN sys.dm_exec_sessions bs ON bs.session_id = r.blocking_session_id
OUTER APPLY sys.dm_exec_sql_text(r.sql_handle) t
WHERE r.blocking_session_id <> 0
ORDER BY r.wait_time DESC
SQL);

section('Locks en paqsystems_pedidosweb_ankasdelsur', <<<'SQL'
SELECT
    l.request_session_id AS session_id,
    s.login_name,
    s.host_name,
    s.program_name,
    s.status,
    l.resource_type,
    l.resource_database_id,
    DB_NAME(l.resource_database_id) AS db_name,
    l.resource_associated_entity_id,
    l.request_mode,
    l.request_status,
    l.request_type
FROM sys.dm_tran_locks l
INNER JOIN sys.dm_exec_sessions s ON s.session_id = l.request_session_id
WHERE l.resource_database_id = DB_ID('paqsystems_pedidosweb_ankasdelsur')
  AND s.is_user_process = 1
ORDER BY l.request_session_id, l.resource_type
SQL);

section('Sesiones PHP / Laravel (ultimas 24h aprox)', <<<'SQL'
SELECT
    session_id,
    login_name,
    host_name,
    program_name,
    DB_NAME(database_id) AS current_db,
    status,
    open_transaction_count,
    last_request_start_time,
    last_request_end_time
FROM sys.dm_exec_sessions
WHERE is_user_process = 1
  AND program_name LIKE '%PHP%'
ORDER BY last_request_start_time DESC
SQL);
