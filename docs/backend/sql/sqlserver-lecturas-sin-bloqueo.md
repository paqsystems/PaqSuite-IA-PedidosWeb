# SQL Server — lecturas sin bloqueo en ERP compartido

| Campo | Valor |
|-------|--------|
| **Estado** | Vigente |
| **Ámbito** | Backend Laravel sobre SQL Server (`DB_CONNECTION=sqlsrv`) |
| **Relacionado** | [SPEC-101-10](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) (lookup artículos / stock informativo) |

## Contexto

PedidosWeb comparte la base ERP con otros procesos (ERP desktop, jobs, grabaciones). Las consultas de **solo lectura** (lookup de artículos, stock disponible, catálogos) no deben retener **shared locks** que bloqueen escrituras ajenas ni quedar bloqueadas por transacciones largas del ERP.

**Trade-off aceptado:** stock y disponible mostrados en listbox/informes pueden leer datos no confirmados (dirty reads). Es aceptable porque son **informativos** para operatoria comercial, no reservan stock ni validan consistencia fuerte.

## Configuración de conexión

En `backend/config/database.php` (driver `sqlsrv`):

| Variable `.env` | Default | Uso |
|-----------------|---------|-----|
| `DB_APP_NAME` | `PedidosWeb` | `program_name` en SSMS para diagnosticar sesiones |
| `DB_ISOLATION_LEVEL` | `READ UNCOMMITTED` | Aislamiento por defecto de la sesión ODBC |

Ejemplo en `backend/.env.example`.

## Reglas de implementación

### Lecturas

1. **Sesión:** `READ UNCOMMITTED` al conectar (config ODBC).
2. **SQL crudo:** `SqlServerReadHint::fromAs('tabla', 'alias')` → `FROM [tabla] AS [alias] WITH (NOLOCK)`.
3. **Aplicado hoy en:** `ArticuloCargaLookupService` (browse de artículos en carga).

### Escrituras

1. Usar `SqlServerIsolation::transaction($callback)` en lugar de `DB::transaction()` directo en servicios que persisten pedidos, permisos, importaciones, etc.
2. Dentro del closure se ejecuta `SET TRANSACTION ISOLATION LEVEL READ COMMITTED` antes de INSERT/UPDATE/DELETE.

### Prohibido

- `NOLOCK` / `READ UNCOMMITTED` en flujos que requieran consistencia fuerte con `lockForUpdate()` o reserva de stock.

## Diagnóstico en SSMS

```sql
-- Sesiones PedidosWeb
SELECT session_id, program_name, status, last_request_start_time
FROM sys.dm_exec_sessions
WHERE program_name LIKE '%PedidosWeb%';

-- Bloqueos activos
SELECT * FROM sys.dm_tran_locks WHERE request_session_id IN (
  SELECT session_id FROM sys.dm_exec_sessions WHERE program_name LIKE '%PedidosWeb%'
);
```

## Referencias en código

| Archivo | Rol |
|---------|-----|
| `backend/app/Support/SqlServerReadHint.php` | Helper `WITH (NOLOCK)` |
| `backend/app/Support/SqlServerIsolation.php` | Transacciones de escritura `READ COMMITTED` |
| `backend/app/Services/PedidosWeb/ArticuloCargaLookupService.php` | Lookup artículos carga |
| `.cursor/rules/sqlserver-read-uncommitted.mdc` | Regla para agentes / PR |

## Deploy

- Sin migraciones ni seeds.
- Verificar en servidor: `DB_CONNECTION=sqlsrv`, `DB_APP_NAME`, `DB_ISOLATION_LEVEL` (opcional; default adecuado).
- Smoke: abrir carga de pedidos con dos usuarios concurrentes; listbox artículos responde sin bloqueo prolongado.
